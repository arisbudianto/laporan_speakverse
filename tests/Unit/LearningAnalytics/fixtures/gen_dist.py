from scipy import stats, special
import json
r={}
r['t']=[[t,df,stats.t.cdf(t,df)] for t,df in [(2.1,9),(-1.3,25),(0.4,3),(3.5,120),(-6,5)]]
r['tq']=[[p,df,stats.t.ppf(p,df)] for p,df in [(0.975,9),(0.975,30),(0.975,2),(0.9,15)]]
r['chi']=[[x,df,stats.chi2.cdf(x,df)] for x,df in [(3.2,2),(12,5),(0.5,1),(40,10)]]
r['f']=[[f,a,b,stats.f.cdf(f,a,b)] for f,a,b in [(2.5,3,20),(0.7,5,40),(6,2,10)]]
r['n']=[[z,stats.norm.cdf(z)] for z in [-2.5,-0.3,0,1.96,3]]
r['lg']=[[x,float(special.gammaln(x))] for x in [0.3,1,2.5,10,57.2]]
print(json.dumps(r))
